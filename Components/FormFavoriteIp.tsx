import React, { Component } from 'react'
import HubletoForm, { HubletoFormProps, HubletoFormState } from '@hubleto/react-ui/ext/HubletoForm';
import Table, { TableProps, TableState } from '@hubleto/react-ui/core/Table';

interface FormFavoriteIpProps extends HubletoFormProps { }
interface FormFavoriteIpState extends HubletoFormState { }

export default class FormFavoriteIp<P, S> extends HubletoForm<FormFavoriteIpProps, FormFavoriteIpState> {
  static defaultProps: any = {
    ...HubletoForm.defaultProps,
    model: 'Hubleto/App/Custom/IpInfoTest/Models/FavoriteIp'
  }

  props: FormFavoriteIpProps;
  state: FormFavoriteIpState;

  translationContext: string = 'Hubleto\\App\\Custom\\IpInfoTest';
  translationContextInner: string = 'Components\\FormFavoriteIp';

  constructor(props: FormFavoriteIpProps) {
    super(props);
  }

  getStateFromProps(props: FormDealProps) {
    return {
      ...super.getStateFromProps(props),
      tabs: [
        { uid: 'default', title: <b>this.translate('FavoriteIp')</b> },
        // Add your tabs here.
        // 'tab_with_nested_table': { title: 'Example tab with nested table' }
      ]
    };
  }

  getRecordFormUrl(): string {
    return 'favorite-ips/' + (this.state.record.id > 0 ? this.state.record.id : 'add');
  }

  renderTitle(): JSX.Element {
    return <>
      <small>FavoriteIp</small>
      <h2>Record #{this.state.record.id ?? '0'}</h2>
    </>;
  }

}
